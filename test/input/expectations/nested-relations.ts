export interface FirstLevel {
  // columns
  id: number
  name: string
  user_id: number
  // relations
  user: User
  second_level_models: SecondLevel[]
}
export type FirstLevelEditable = Pick<FirstLevel, 'name'> & {
  second_level_models: SecondLevelEditable[]
}
